import copy

class Gene:
    def __init__(self, section_id, timeslot_id, room_ids):
        
        self.section_id = section_id
        self.timeslot_id = timeslot_id
        # Đảm bảo room_ids luôn lưu trữ dưới dạng một danh sách các phòng được chọn (R_selected)
        self.room_ids = list(room_ids) if isinstance(room_ids, (list, set, tuple)) else [room_ids]

    def __repr__(self):
        return f"Gene(Section: {self.section_id}, Timeslot: {self.timeslot_id}, Rooms: {self.room_ids})"

    def clone(self):
        return Gene(self.section_id, self.timeslot_id, list(self.room_ids))


class Chromosome:
   
    def __init__(self, genes=None):
       
        self.genes = genes if genes is not None else []
        self.fitness = 0.0  # Chỉ số độ thích nghi của phương án, dao động trong khoảng (0, 1]

    def add_gene(self, gene):
        if isinstance(gene, Gene):
            self.genes.append(gene)
        else:
            raise TypeError("Đối tượng thêm vào phải là một thực thể thuộc lớp Gene.")

    def get_gene_by_section(self, section_id):
        for gene in self.genes:
            if gene.section_id == section_id:
                return gene
        return None

    def clone(self):
       
        cloned_chromosome = Chromosome()
        cloned_chromosome.genes = [gene.clone() for gene in self.genes]
        cloned_chromosome.fitness = self.fitness
        return cloned_chromosome

    def __len__(self):
        """Trả về số lượng Gene bên trong nhiễm sắc thể (tổng số lớp học phần đã được sắp xếp)."""
        return len(self.genes)

    def __repr__(self):
        return f"Chromosome(Total_Genes: {len(self.genes)}, Fitness: {self.fitness:.6f})"
